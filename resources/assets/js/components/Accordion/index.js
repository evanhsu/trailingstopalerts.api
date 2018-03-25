import React from 'react';
import PropTypes from 'prop-types';
import ImmutablePropTypes from 'react-immutable-proptypes';
import Button from 'material-ui/Button';
import AddIcon from 'material-ui-icons/Add';
import { Map } from 'immutable';
import { Typography } from "material-ui";
import { withStyles } from 'material-ui/styles';
import StopAlertPanel from '../StopAlertPanel';
import StopAlertCreateForm from '../../containers/StopAlertCreateForm';

const styles = theme => ({
  root: {},
  accordionRoot: {
    flexGrow: 1,
  },
  button: {},
  heading: {
    display: 'flex',
    alignItems: 'center',
    paddingBottom: 15,
  },
  headingText: {
    fontSize: 40,
    paddingRight: 10,
  },
  newAlertForm: {

  },
});

class Accordion extends React.Component {
  state = {
    expandedPanel: null,
    newAlertFormIsVisible: false,
  };

  handlePanelClick = panel => (event, expanded) => {
    this.setState({
      expandedPanel: expanded ? panel : false,
    });
  };

  openNewAlertForm = () => {
    this.setState({
      newAlertFormIsVisible: true,
    });
  };

  closeNewAlertForm = () => {
    this.setState({
      newAlertFormIsVisible: false,
    });
  };

  render() {
    const { classes } = this.props;
    const { expandedPanel } = this.state;

    const stopAlerts = this.props.stopAlerts.valueSeq().map((stopAlert) => (
      <StopAlertPanel
        key={stopAlert.get('id').toString()}
        stopAlert={stopAlert}
        expanded={expandedPanel === stopAlert.get('symbol')}
        onChange={this.handlePanelClick(stopAlert.get('symbol'))}
      />
    ));

    return (
      <div className={classes.root}>
        <div className={classes.heading}>
          <Typography className={classes.headingText}>Alerts</Typography>
          <Button mini variant="fab" color="secondary" aria-label="add" className={classes.button} onClick={this.openNewAlertForm}>
            <AddIcon />
          </Button>
        </div>
        { this.state.newAlertFormIsVisible ? (<div className={classes.newAlertForm}><StopAlertCreateForm onRequestClose={this.closeNewAlertForm}/></div>) : null }
        <div className={classes.accordionRoot}>
          {stopAlerts}
        </div>
      </div>
    );
  }
}

Accordion.propTypes = {
  classes: PropTypes.object.isRequired,
  stopAlerts: ImmutablePropTypes.map,
};

Accordion.defaultProps = {
  stopAlerts: new Map(),
};

export default withStyles(styles)(Accordion);